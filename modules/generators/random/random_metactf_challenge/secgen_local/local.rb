#!/usr/bin/ruby
require_relative '../../../../../lib/objects/local_string_generator.rb'

class RandomMetaCTFChallenge < StringGenerator
  attr_accessor :difficulty

  def initialize
    super
    self.module_name = 'Random Wordpress Version Generator'
    self.difficulty = 'easy'
  end

  def generate
    # TODO : Adjust the difficulties

    src_angr = [
        {:difficulty => 'easy', :name => 'src_angr/00_angr_find'},
        {:difficulty => 'easy', :name => 'src_angr/01_angr_avoid'},
        {:difficulty => 'easy', :name => 'src_angr/02_angr_find_condition'},
        {:difficulty => 'easy', :name => 'src_angr/03_angr_symbolic_registers'},
        {:difficulty => 'easy', :name => 'src_angr/04_angr_symbolic_stack'},
        {:difficulty => 'easy', :name => 'src_angr/05_angr_symbolic_memory'},
        {:difficulty => 'easy', :name => 'src_angr/06_angr_symbolic_dynamic_memory'},
        {:difficulty => 'easy', :name => 'src_angr/07_angr_symbolic_file'},
        {:difficulty => 'easy', :name => 'src_angr/08_angr_constraints'},
        {:difficulty => 'easy', :name => 'src_angr/09_angr_hooks'},
        {:difficulty => 'easy', :name => 'src_angr/10_angr_simprocedures'},
        {:difficulty => 'easy', :name => 'src_angr/11_angr_sim_scanf'},
        {:difficulty => 'easy', :name => 'src_angr/12_angr_veritesting'},
        {:difficulty => 'easy', :name => 'src_angr/13_angr_static_binary'},
        {:difficulty => 'easy', :name => 'src_angr/14_angr_shared_library'},
        {:difficulty => 'easy', :name => 'src_angr/15_angr_arbitrary_read'},
        {:difficulty => 'easy', :name => 'src_angr/16_angr_arbitrary_write'},
        {:difficulty => 'easy', :name => 'src_angr/17_angr_arbitrary_jump'},
    ]

    src_csp = [
        {:difficulty => 'easy', :name => 'src_csp/Ch1-2/Ch1_Ltrace'},
        {:difficulty => 'easy', :name => 'src_csp/Ch1-2/Ch1_Readelf'},
        {:difficulty => 'easy', :name => 'src_csp/Ch1-2/Ch2_01_Endian'},
        {:difficulty => 'easy', :name => 'src_csp/Ch1-2/Ch2_01_Showkey'},
        {:difficulty => 'easy', :name => 'src_csp/Ch1-2/Ch2_03_IntOverflow'},
        {:difficulty => 'easy', :name => 'src_csp/Ch1-2/Ch2_03_TwosComplement'},
        {:difficulty => 'easy', :name => 'src_csp/Ch1-2/Ch2_03_XorInt'},
        {:difficulty => 'easy', :name => 'src_csp/Ch1-2/Ch2_05_FloatConvert'},
        {:difficulty => 'easy', :name => 'src_csp/Ch3-ExtraFormatStr/Ch3_Format0_Leak'},
        {:difficulty => 'easy', :name => 'src_csp/Ch3-ExtraFormatStr/Ch3_Format1_LeakDollar'},
        {:difficulty => 'easy', :name => 'src_csp/Ch3-ExtraFormatStr/Ch3_Format2_nCorruptKey'},
        {:difficulty => 'easy', :name => 'src_csp/Ch3-ExtraFormatStr/Ch3_Format3_nWriteKey'},
        {:difficulty => 'easy', :name => 'src_csp/Ch3-ExtraFormatStr/Ch3_Format4_nWriteFnP'},
        {:difficulty => 'easy', :name => 'src_csp/Ch3-ExtraFormatStr/Ch3_Format5_nTargetWrite'},
        {:difficulty => 'easy', :name => 'src_csp/Ch3-ExtraFormatStr/Ch3_Format6_PLTHijack'},
        {:difficulty => 'easy', :name => 'src_csp/Ch3.1-3.5/Ch3_00_GdbIntro'},
        {:difficulty => 'easy', :name => 'src_csp/Ch3.1-3.5/Ch3_00_GdbRegs'},
        {:difficulty => 'easy', :name => 'src_csp/Ch3.1-3.5/Ch3_00_GdbSetmem'},
        {:difficulty => 'easy', :name => 'src_csp/Ch3.1-3.5/Ch3_01_GdbPractice'},
        {:difficulty => 'easy', :name => 'src_csp/Ch3.1-3.5/Ch3_02_AsciiInstr'},
        {:difficulty => 'easy', :name => 'src_csp/Ch3.1-3.5/Ch3_04_FnPointer'},
        {:difficulty => 'easy', :name => 'src_csp/Ch3.1-3.5/Ch3_04_LinkedList'},
        {:difficulty => 'easy', :name => 'src_csp/Ch3.1-3.5/Ch3_05_XorLong'},
        {:difficulty => 'easy', :name => 'src_csp/Ch3.1-3.5/Ch3_05_XorStr'},
        {:difficulty => 'easy', :name => 'src_csp/Ch3.6-3.7/Ch3_06_Conditionals'},
        {:difficulty => 'easy', :name => 'src_csp/Ch3.6-3.7/Ch3_06_LoopMulti'},
        {:difficulty => 'easy', :name => 'src_csp/Ch3.6-3.7/Ch3_06_SwitchTable'},
        {:difficulty => 'easy', :name => 'src_csp/Ch3.6-3.7/Ch3_07_FloatReturn'},
        {:difficulty => 'easy', :name => 'src_csp/Ch3.6-3.7/Ch3_07_ParamsRegs'},
        {:difficulty => 'easy', :name => 'src_csp/Ch3.6-3.7/Ch3_07_ParamsStack'},
        {:difficulty => 'easy', :name => 'src_csp/Ch3.6-3.7/Ch3_07_SegvBacktrace'},
        {:difficulty => 'easy', :name => 'src_csp/Ch3.6-3.7/Ch3_07_StaticInt'},
        {:difficulty => 'easy', :name => 'src_csp/Ch3.6-3.7/Ch3_07_StaticStrcmp'},
        {:difficulty => 'easy', :name => 'src_csp/Ch3.7-3.9/Ch3_07_CanaryBypass'},
        {:difficulty => 'easy', :name => 'src_csp/Ch3.7-3.9/Ch3_07_HijackPLT'},
        {:difficulty => 'easy', :name => 'src_csp/Ch3.7-3.9/Ch3_07_ScanfOverflow'},
        {:difficulty => 'easy', :name => 'src_csp/Ch3.7-3.9/Ch3_07_StackSmash'},
        {:difficulty => 'easy', :name => 'src_csp/Ch3.7-3.9/Ch3_08_2DArrays'},
        {:difficulty => 'easy', :name => 'src_csp/Ch3.7-3.9/Ch3_08_Matrix'},
        {:difficulty => 'easy', :name => 'src_csp/Ch3.7-3.9/Ch3_09_Structs'},
        {:difficulty => 'easy', :name => 'src_csp/Ch5-8/Ch5_08_LoopUnroll'},
        {:difficulty => 'easy', :name => 'src_csp/Ch5-8/Ch7_13_LdPreloadGetUID'},
        {:difficulty => 'easy', :name => 'src_csp/Ch5-8/Ch8_05_PsSignals'},
        {:difficulty => 'easy', :name => 'src_csp/Ch5-8/Ch8_05_Signals'},
    ]

    src_malware = [
        {:difficulty => 'easy', :name => 'src_malware/Ch01-08/Ch01StatA_Readelf'},
        {:difficulty => 'easy', :name => 'src_malware/Ch01-08/Ch03DynA_Ltrace'},
        {:difficulty => 'easy', :name => 'src_malware/Ch01-08/Ch04x86_AsciiInstr'},
        {:difficulty => 'easy', :name => 'src_malware/Ch01-08/Ch04x86_AsciiStrcmp'},
        {:difficulty => 'easy', :name => 'src_malware/Ch01-08/Ch06CAsm_Conditionals'},
        {:difficulty => 'easy', :name => 'src_malware/Ch01-08/Ch06CAsm_LinkedList'},
        {:difficulty => 'easy', :name => 'src_malware/Ch01-08/Ch06CAsm_LoopMulti'},
        {:difficulty => 'easy', :name => 'src_malware/Ch01-08/Ch06CAsm_SwitchTable'},
        {:difficulty => 'easy', :name => 'src_malware/Ch01-08/Ch08Dbg_GdbIntro'},
        {:difficulty => 'easy', :name => 'src_malware/Ch01-08/Ch08Dbg_GdbParams'},
        {:difficulty => 'easy', :name => 'src_malware/Ch01-08/Ch08Dbg_GdbPractice'},
        {:difficulty => 'easy', :name => 'src_malware/Ch01-08/Ch08Dbg_GdbRegs'},
        {:difficulty => 'easy', :name => 'src_malware/Ch01-08/Ch08Dbg_GdbSetmem'},
        {:difficulty => 'easy', :name => 'src_malware/Ch01-08/Ch08Dbg_InputFormat'},
        {:difficulty => 'easy', :name => 'src_malware/Ch01-08/Ch08Dbg_Radare2Intro1'},
        {:difficulty => 'easy', :name => 'src_malware/Ch01-08/Ch08Dbg_Radare2Intro2'},
        {:difficulty => 'easy', :name => 'src_malware/Ch01-08/Ch08Dbg_StaticInt'},
        {:difficulty => 'easy', :name => 'src_malware/Ch01-08/Ch08Dbg_StaticRE'},
        {:difficulty => 'easy', :name => 'src_malware/Ch01-08/Ch08Dbg_StaticStrcmp'},
        {:difficulty => 'easy', :name => 'src_malware/Ch11-13/Ch11MalBeh_HijackPLT'},
        {:difficulty => 'easy', :name => 'src_malware/Ch11-13/Ch11MalBeh_LdPreloadGetUID'},
        {:difficulty => 'easy', :name => 'src_malware/Ch11-13/Ch11MalBeh_LdPreloadRand'},
        {:difficulty => 'easy', :name => 'src_malware/Ch11-13/Ch11MalBeh_NetcatShovel'},
        {:difficulty => 'easy', :name => 'src_malware/Ch11-13/Ch12Covert_ForkFollow'},
        {:difficulty => 'easy', :name => 'src_malware/Ch11-13/Ch12Covert_ForkPipe'},
        {:difficulty => 'easy', :name => 'src_malware/Ch11-13/Ch13DataEnc_BaseEnc'},
        {:difficulty => 'easy', :name => 'src_malware/Ch11-13/Ch13DataEnc_XorEnc'},
        {:difficulty => 'easy', :name => 'src_malware/Ch15-16/Ch15AntiDis_FakeCallInt'},
        {:difficulty => 'easy', :name => 'src_malware/Ch15-16/Ch15AntiDis_FakeCond'},
        {:difficulty => 'easy', :name => 'src_malware/Ch15-16/Ch15AntiDis_FakeMetaConds'},
        {:difficulty => 'easy', :name => 'src_malware/Ch15-16/Ch15AntiDis_InJmp'},
        {:difficulty => 'easy', :name => 'src_malware/Ch15-16/Ch15AntiDis_PushRet'},
        {:difficulty => 'easy', :name => 'src_malware/Ch15-16/Ch16AntiDbg_BypassPtrace'},
        {:difficulty => 'easy', :name => 'src_malware/Ch15-16/Ch16AntiDbg_GdbCheckTrace'},
        {:difficulty => 'easy', :name => 'src_malware/Ch15-16/Ch16AntiDbg_Int3Scan'},
        {:difficulty => 'easy', :name => 'src_malware/Ch15-16/Ch16AntiDbg_SigtrapCheck'},
        {:difficulty => 'easy', :name => 'src_malware/Ch15-16/Ch16AntiDbg_SigtrapEntangle'},
        {:difficulty => 'easy', :name => 'src_malware/Ch15-16/Ch16AntiDbg_SigtrapHijack'},
        {:difficulty => 'easy', :name => 'src_malware/Ch15-16/Ch16AntiDbg_TimeCheck'},
        {:difficulty => 'easy', :name => 'src_malware/Ch18-21/Ch18PackUnp_UnpackEasy'},
        {:difficulty => 'easy', :name => 'src_malware/Ch18-21/Ch18PackUnp_UnpackGdb'},
        {:difficulty => 'easy', :name => 'src_malware/Ch18-21/Ch21x64_ParamsRegs'},
        {:difficulty => 'easy', :name => 'src_malware/Ch18-21/Ch21x64_ParamsStack'},
    ]

    challenges = src_angr + src_csp + src_malware

    # Select based on difficulty

    challenges.delete_if do |challenge|
        challenge[:difficulty] != self.difficulty
    end

    challenge = challenges.sample
    outputs << challenge[:name]

  end

end

RandomMetaCTFChallenge.new.run